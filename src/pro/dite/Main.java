package pro.dite;

import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.lib.PersonIdent;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Hashtable;
import java.util.Set;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        final Hashtable<String, HashSet<PersonIdent>> index = new Hashtable<String, HashSet<PersonIdent>>();
        File repoDir = new File("/Users/mikulas/Projects/khanovaskola.cz-v3/.git");
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            public void processFileDiff(RevCommit commit, EditList edits, PhpFile a, PhpFile b)
            {
//                System.out.println(commit.getId() + " " + commit.getShortMessage());
                for (Edit edit : edits)
                {
                    if (edit.getType() == Edit.Type.REPLACE || edit.getType() == Edit.Type.DELETE)
                    {
                        for (int i = edit.getBeginA(); i < edit.getEndA(); ++i)
                        {
                            PhpFile.Line line = a.lines.get(i);
                            if (!index.containsKey(line.id))
                            {
                                index.put(line.id, new HashSet<PersonIdent>());
                            }
                            index.get(line.id).add(commit.getAuthorIdent());
                        }
                    }
                    if (edit.getType() == Edit.Type.REPLACE || edit.getType() == Edit.Type.INSERT)
                    {
                        for (int i = edit.getBeginB(); i < edit.getEndB(); ++i)
                        {
                            PhpFile.Line line = b.lines.get(i);
                            if (!index.containsKey(line.id))
                            {
                                index.put(line.id, new HashSet<PersonIdent>());
                            }
                            index.get(line.id).add(commit.getAuthorIdent());
                        }
                    }
                }
            }
        };

        walker.walk();
    }

}
