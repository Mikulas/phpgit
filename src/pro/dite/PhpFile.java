package pro.dite;

import com.sun.xml.internal.xsom.impl.Ref;

import java.io.IOException;
import java.util.*;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class PhpFile
{

    ArrayList<Line> lines = new ArrayList<Line>();

    public PhpFile(String content) throws EmptyStackException
    {
        if (content.equals(""))
        {
            return;
        }

        Stack<Context> context = new Stack<Context>();

        content = removeQuoted(content, "\"(?>[^\\n\\\\\"]|\\\\.)*\"");
        content = removeQuoted(content, "'(?>[^\\n\\\\']|\\\\.)*'");
        content = removeComments(content);
        content = removeQuoted(content, "\"(?>[^\\\\\"]|\\\\.)*\"");
        content = removeQuoted(content, "'(?>[^\\\\']|\\\\.)*'");

        Pattern pNamespace = Pattern.compile("namespace\\s+(?<namespace>[\\\\a-zA-Z_\\x7f-\\xff][\\\\a-zA-Z0-9_\\x7f-\\xff]*)");
        Pattern pClass = Pattern.compile("class\\s+(?<className>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)");
        Pattern pFunction = Pattern.compile("function\\s+(?<functionName>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)");
        Boolean ignoreNextOpeningBracket = false;

        String[] split;
        try {
            split = content.split("(?=\\r?\\n)");
        } catch (StackOverflowError e)
        {
            return;
        }

        for (String line : split)
        {
            line = line.trim();
            Matcher mNamespace = pNamespace.matcher(line);
            Matcher mClass = pClass.matcher(line);
            Matcher mFunction = pFunction.matcher(line);

            if (mClass.find())
            {
                context.add(new Context(ContextType.CLASS, mClass.group("className")));
                ignoreNextOpeningBracket = true;
            }
            else if (mFunction.find())
            {
                context.add(new Context(ContextType.FUNCTION, mFunction.group("functionName")));
                ignoreNextOpeningBracket = true;
            }
            else if (mNamespace.find())
            {
                context.add(new Context(ContextType.NAMESPACE, mNamespace.group("namespace")));
            }

            for (int i = countOccurrences(line, "{"); i > 0; --i)
            {
                if (!ignoreNextOpeningBracket)
                {
                    context.add(new Context());
                }
                ignoreNextOpeningBracket = false;
            }

            for (int i = countOccurrences(line, "}"); i > 0; --i)
            {
                context.pop();
            }
            lines.add(new Line(context));
        }
        lines.add(new Line(context)); // empty line at end of file // TODO check
    }

    private String removeComments(String in)
    {
        // TODO this is not ok if if was in string
        Matcher mHash = Pattern.compile("(?>#|//).*$", Pattern.MULTILINE).matcher(in);
        in = mHash.replaceAll("");

        StringBuffer resultString = new StringBuffer();
        Matcher mComment = Pattern.compile("/\\*.*?\\*/", Pattern.DOTALL).matcher(in);
        while (mComment.find()) {
            int lastIndex = 0;
            StringBuilder replacement = new StringBuilder();
            for (int i = countOccurrences(mComment.group(), "\n"); i > 0; --i)
            {
                replacement.append("\n");
            }

            mComment.appendReplacement(resultString, replacement.toString());
        }
        mComment.appendTail(resultString);

        return resultString.toString();
    }

    private String removeQuoted(String in, String regex)
    {
        StringBuffer resultString = new StringBuffer();
        Matcher mDouble = Pattern.compile(regex, Pattern.MULTILINE).matcher(in);
        while (mDouble.find()) {
            int lastIndex = 0;
            StringBuilder replacement = new StringBuilder();
            for (int i = countOccurrences(mDouble.group(), "\n"); i > 0; --i)
            {
                replacement.append("\n");
            }

            mDouble.appendReplacement(resultString, replacement.toString());
        }
        mDouble.appendTail(resultString);

        return resultString.toString();
    }

    private int countOccurrences(String in, String find)
    {
        int count = 0;
        int lastIndex = 0;
        StringBuilder replacement = new StringBuilder();
        while (lastIndex != -1)
        {
            lastIndex = in.indexOf(find, lastIndex);
            if (lastIndex != -1)
            {
                count++;
                lastIndex += find.length();
            }
        }
        return count;
    }

    @Override
    public String toString()
    {
        StringBuilder str = new StringBuilder();
        int lineNum = 1;
        for (Line line : lines)
        {
            str.append(lineNum);
            str.append(": " + line + "\n");
            lineNum++;
        }
        return str.toString();
    }

    public static enum ContextType
    {
        NONE,
        CLASS,
        NAMESPACE,
        FUNCTION,
    }

    public class Context
    {
        ContextType context;
        String name;

        public Context()
        {
            this.context = ContextType.NONE;
        }

        public Context(ContextType context, String name)
        {
            this.context = context;
            this.name = name;
        }
    }

    public class Line
    {
        String id;

        public Line(Stack<Context> context)
        {
            StringBuilder str = new StringBuilder();
            Context last = null;
            for (Context c : context)
            {
                if (c.context == ContextType.NONE)
                {
                    continue;
                }
                else if (last != null
                    && last.context == ContextType.CLASS
                    && c.context == ContextType.FUNCTION)
                {
                    str.append("::");
                }
                else
                {
                    str.append("\\");
                }
                str.append(c.name);
                last = c;
            }
            id = str.toString();
        }

        @Override
        public String toString()
        {
            return "Line{" +
                    "id=" + id +
                    '}';
        }
    }
}
