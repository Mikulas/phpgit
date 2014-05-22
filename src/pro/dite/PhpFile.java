package pro.dite;

import java.io.IOException;
import java.util.*;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class PhpFile
{

    ArrayList<Line> lines = new ArrayList<Line>();

    public PhpFile(String content)
    {
        Stack<Context> context = new Stack<Context>();

        content = removeQuoted(content, "\"(?:[^\\\\\"]|\\\\.)*\"");
        content = removeQuoted(content, "'(?:[^\\\\']|\\\\.)*'");

        Pattern pClass = Pattern.compile("class\\s+(?<className>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)");
        Pattern pFunction = Pattern.compile("function\\s+(?<functionName>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)");
        for (String line : content.split("\\r?\\n"))
        {
            Matcher mClass = pClass.matcher(line);
            Matcher mFunction = pFunction.matcher(line);
            if (mClass.find())
            {
                context.add(new Context(ContextType.CLASS, mClass.group("className")));
            }
            else if (mFunction.find())
            {
                context.add(new Context(ContextType.FUNCTION, mFunction.group("functionName")));
            }
            // TODO pop context on }
            lines.add(new Line(context));
        }
    }

    private String removeQuoted(String in, String regex)
    {
        StringBuffer resultString = new StringBuffer();
        Matcher mDouble = Pattern.compile(regex).matcher(in);
        while (mDouble.find()) {
            int lastIndex = 0;
            StringBuilder replacement = new StringBuilder();
            while (lastIndex != -1)
            {
                lastIndex = mDouble.group().indexOf("\n", lastIndex);
                if (lastIndex != -1)
                {
                    replacement.append("\n");
                    lastIndex++;
                }
            }

            mDouble.appendReplacement(resultString, replacement.toString());
        }
        mDouble.appendTail(resultString);

        return resultString.toString();
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
        FUNCTION
    }

    public class Context
    {
        ContextType context;
        String name;

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
                if (last != null
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
