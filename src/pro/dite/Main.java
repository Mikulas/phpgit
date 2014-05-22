package pro.dite;

import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Paths;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        File repoDir = new File("/Users/mikulas/Projects/khanovaskola.cz-v3/.git");
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            public void processFileDiff(RevCommit commit, EditList edits, String a, String b)
            {
//                System.out.println(commit.getShortMessage());
                for (Edit edit : edits)
                {
//                    System.out.println("\t" + edit);
                }
            }
        };

        walker.walk();
    }
}
